FROM        ubuntu:xenial
MAINTAINER  Erik Hedenström <erik@hedenstroem.com>

ADD exim4.debconf /tmp/

RUN apt-get update && \
    apt-get -y upgrade && \
    debconf-set-selections < /tmp/exim4.debconf && \
    DEBCONF_FRONTEND=noninteractive apt-get -y install exim4-daemon-light && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN echo "net.ipv6.conf.all.disable_ipv6 = 1"		>> /etc/sysctl.d/99-sysctl.conf && \
	echo "net.ipv6.conf.default.disable_ipv6 = 1"	>> /etc/sysctl.d/99-sysctl.conf && \
	echo "net.ipv6.conf.lo.disable_ipv6 = 1"		>> /etc/sysctl.d/99-sysctl.conf && \
	echo "disable_ipv6 = true"	>> /etc/exim4/conf.d/main/02_exim4-config_options && \
	sysctl -p

EXPOSE 25
ENTRYPOINT [ "/usr/sbin/exim" ]
CMD [ "-v", "-bdf", "-q7m" ]
