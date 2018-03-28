Nginx Reverse Proxy Recipe
==========================

This recipe based on the `jwilder/nginx-proxy` docker proxy image.

## Parameters

```yaml
# We can us it
name: test-page

# ...
recipes:
    nginx_reverse_proxy:

        # The nginx-reverse-proxy network name.
        network_name:         reverse-proxy

        # You have to set the service and its host and port settings.
        settings:

            # Same solutin, different way
            web: ~ # --> use the `name`: test-page.loc:80
            # Only port
            web: 81 # --> use the `name`: test-page.loc:81
            # Direct set host and port
            web:
                host: test.loc
                port: 82
```
