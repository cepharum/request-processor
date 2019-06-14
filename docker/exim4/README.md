# hedenstroem/exim4

[![build status](https://gitlab.hedenstroem.com/docker/exim4/badges/master/build.svg)](https://gitlab.hedenstroem.com/docker/exim4/commits/master)
[![Docker Stars](https://img.shields.io/docker/stars/hedenstroem/exim4.svg)][hub]
[![Docker Pulls](https://img.shields.io/docker/pulls/hedenstroem/exim4.svg)][hub]

A Docker image based on [Ubuntu][ubuntu] that provides [Exim][exim].

## Example: Starting the proxy (arm)

```bash
docker run --rm -it -p 25:25 hedenstroem/exim4
```
## License

The code in this repository, unless otherwise noted, is MIT licensed. See the `LICENSE` file in this repository.

[ubuntu]: http://www.ubuntu.com/
[exim]: http://www.exim.org/
[hub]: https://hub.docker.com/r/hedenstroem/exim4/
