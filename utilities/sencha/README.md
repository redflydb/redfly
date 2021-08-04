The Sencha Cmd image
--------------------

This is an image for working with Sencha Cmd, a tool for managing ExtJS projects.

The image is self-contained. Simply run, e.g.

```
docker build . -t sencha
```

to build the image.

Run the image as if you are running Sencha Cmd directly; just pass the arguments to `docker run` as if you are passing them directly to `sencha`. For example, to install the framework in an existing project, run:

```
docker run --rm -tv $PWD:/sencha sencha app install
```

For more information on Sencha Cmd, see the documentation [here](https://docs.sencha.com/cmd/6.5.1/index.html).
