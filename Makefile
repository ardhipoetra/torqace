#
# Makefile used to install/update PBSWeb-Lite
#

## The "document root" of the PBSWeb-Lite installation,
## all PHP scripts will be installed into this directory.
INSTALLDIR = /var/www/lite/v2

## username that runs apache server.
HTTPUSER = apache

## You don't have to change anything after this line.
CC = gcc
CFLAGS = -O2 -s
LFLAGS = -lcrypt

all: spasswd

spasswd: spasswd.c
	gcc $(CFLAGS) -o spasswd $(LFLAGS) spasswd.c

install: all
	if [ ! -d $(INSTALLDIR) ]; then mkdir -p $(INSTALLDIR); fi
	cp -r * $(INSTALLDIR)/
	chown -R $(HTTPUSER).$(HTTPUSER) $(INSTALLDIR)
	find $(INSTALLDIR) -type f | xargs chmod 644
	find $(INSTALLDIR) -type d | xargs chmod 755
	install -m 755 spasswd /usr/sbin/spasswd
	chown root.root /usr/sbin/spasswd
	chmod u+s /usr/sbin/spasswd

clean:
	rm -f *~ spasswd


#
# $Id: Makefile,v 1.8 2004/03/19 03:42:21 platin Exp $
#
