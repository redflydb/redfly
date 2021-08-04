FROM openjdk:8-jre-stretch

# Download and install the Sencha Cmd tool and ExtJS 6.2.0 framework.
RUN wget -qO - http://cdn.sencha.com/cmd/6.2.2/no-jre/SenchaCmd-6.2.2-linux-amd64.sh.zip | funzip > /install.sh && \
    sh /install.sh -q -dir /opt/Sencha/Cmd && \
    rm /install.sh && \
    wget -q http://cdn.sencha.com/ext/gpl/ext-6.2.0-gpl.zip && \
    unzip -qq /ext-6.2.0-gpl.zip -d /sencha-sdks && \
    cd /sencha-sdks/ext-6.2.0/ && /opt/Sencha/Cmd/sencha package upgrade && \
    rm -rf /ext-6.2.0-gpl.zip

# Expose a mount point for mounting and working with the ExtJS project files.
VOLUME ["/sencha"]
WORKDIR /sencha

# Set the Sencha Cmd utility as the container entrypoint.
ENTRYPOINT ["/opt/Sencha/Cmd/sencha"]
