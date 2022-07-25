# Kubernetes PHP-fpm graceful restart

## Description of the problem
When PHP-fpm is being restarted during rolling update on the kubernetes cluster if a client has an open connection to the fpm-pod it will be closed, and this will provoke a 502 Bad Gateway error.

## Explanation and hints

In order to solve this, we need to find a way to drain the connections. For this reason we can use the kubernetes lifecycle hooks.

One such hook is `preStop` which has to have a graceful shutdown period. Which needs to be shorter than the graceful shutdown period of the Nginx's pod.
Also, we need to specify a `terminationGracePeriodSeconds` which by **default** is `30s`.

### FPM

Inside the **FPM's _Dockerfile_** we have
```dockerfile
RUN echo "process_control_timeout=5s" >> /usr/local/etc/php/conf.d/graceful.ini
```

This `process_control_timeout` ini setting gives the child processes of the fpm time to finish their work before being killed.

_Though this is not enough..._

### Kubernetes
```yaml
[fpm-deployment.yaml]

        containers:
          ...
          lifecycle:
            preStop:
              exec:
                command:
                  - sh
                  - '-c'
                  - sleep 10
      terminationGracePeriodSeconds: 30
```

Please note, that `preStop` starts running after kubernetes has sent the event to the ingress to stop the traffic to this service.
But since this is done **asynchronously**, there might be some traffic that reaches the fpm before **ingress** updates its state.

### NOTE:
`process_control_timeout=5s"` - 5 seconds is the time, in which the last request needs to be done. If your application needs more time, for whatever reason, please adjust the other values as well.

`process_control_timeout` should be less than `preStop`, and `preStop` should be also less than the `terminationGracePeriodSeconds`

### Special Thanks
Special thanks to `Божидар Х.` who reported that Kubernetes is not actually killing the pods with `SIGTERM` by default, but respects the Dockerfile's `STOPSIGNAL`, which for fpm is `SIGQUIT`.
