<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css"
          href="/doc/assets/swagger/swagger-ui.css"/>
<!--        <link rel="stylesheet" href="/doc/assets/swagger/themes/theme-monokai.css" />-->
    <link rel="icon" type="image/png"
          href="/doc/assets/swagger/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png"
          href="/doc/assets/swagger/favicon-16x16.png" sizes="16x16"/>
<!--    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/default.min.css">-->
<!--    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/monokai-sublime.min.css">-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/agate.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/highlight.min.js"></script>
    <script src="/doc/assets/swagger/jquery-3.6.0.min.js"></script>
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="/doc/assets/swagger/swagger-ui-bundle.js" charset="UTF-8"></script>
<script src="/doc/assets/swagger/swagger-ui-standalone-preset.js"
        charset="UTF-8"></script>
<script>
    window.onload = function () {
        var full = location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '');
        // Begin Swagger UI call region
        const ui = SwaggerUIBundle({
            url: "<?php echo $fileSwagger; ?>",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            filter: "",
            syntaxHighlight: {
                activated: true,
                theme: "agate"
            },
            useUnsafeMarkdown: true,
            persistAuthorization: true,
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout",
            oauth2RedirectUrl: "<?php echo \yii\helpers\Url::to('/doc/oauth', true); ?>",
            onComplete: function (){
                document.querySelectorAll('.renderedMarkdown pre code').forEach((el) => {
                    hljs.highlightElement(el);
                });
            }
        });
        // End Swagger UI call region

        window.ui = ui;
    };
</script>
<script>
    $("html").on("click", ".card-head", function() {
        $(this).next(".card-body").toggle();
    });

    document.addEventListener('keypress', event => {
        if(event.code == 'Backquote'){
            document.querySelector('.download-url-button.button').click()
        }
    })
</script>

</body>
</html>
