# Custom nginx error pages (Symfony recipe!)

Nginx default uses hard coded error pages. If you are using http auth for example, you may need a nice `401` error page.

## Create an extra configuration

Create a simple error configuration file to `.docker/web/error_pages.conf`. We will directly include it into the `server`
config block:

```nginx
error_page 401 /401.html;
location = /401.html {
    auth_basic off;
    root /usr/share/nginx/html/error;
    internal;
}

error_page 500 502 503 504 /50x.html;
location = /50x.html {
    auth_basic off;
    root /usr/share/nginx/html/error;
    internal;
}
```

## Create the htmls

This is a simple example for "nice" error page, with Twitter Bootstrap, you can use it to `.docker/web/errors/401.html` and `.docker/web/errors/50x.html`:

```html
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        body {
            background: url(https://www.sliit.lk/wp-content/uploads/2018/02/minimalizm-gradient-background.jpg) no-repeat top center;
            background-size: cover;
            color: white;
            padding: 120px 0;
            min-height: 100vh;
        }

        p {
            font-size: 24px;
        }

        a {
            color: #9fee0e;
        }

        a:hover {
            color: #eeed28;
        }
    </style>

    <title>Krisztián Ferenczi | Symfony expert</title>
</head>
<body>
<div class="container">
    <h1>401 Oops, you are unauthorized :(</h1>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
```

## Configure the project

Now there are 3 different files:

```
[project]/.docker
├── [...]
├── web
│   ├── errors
│   │   ├── 401.html
│   │   └── 50x.html
│   └── error_pages.conf
└── [...]
```

Now you can set the files:

```yaml
# Config the docker compose data.
docker_compose:
    # Docker Compose yaml configuration. You mustn't use the version parameter, it will be automatically.
    extension:
        services:
            # [...]

            web:
                volumes:
                    # "Load" the html files
                    - "%wf.project_path%/.docker/web/error:/usr/share/nginx/html/error"
                    #                                      ^^^^^^^^^^^^^^^^^^^^^^^^^^^ We used this target in the error_pages.conf file!

            # [...]
recipes:
    # Symfony 4 recipe
    # List all available options: `wf --config-dump --recipe=symfony4`
    symfony4:
        # [...]

        nginx:
            # This file will be included in default.conf into the `server` block!
            include_file: "%wf.project_path%/.docker/web/error_pages.conf:/etc/nginx/error_pages.conf"

        # [...]
```
