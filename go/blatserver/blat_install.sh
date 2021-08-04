sudo yum -y install libpng-devel glibc-devel
rm -rf /tmp/blat
mkdir /tmp/blat
unzip -q ./assets/blat.zip -d /tmp/blat/
sudo mkdir -p /root/bin/x86_64
(cd /tmp/blat/blatSrc && sudo MACHTYPE=x86_64 make)
sudo mv /root/bin/x86_64/blat /usr/local/bin/
