Dnsmasq troubleshooting
=======================

`Dnsmasq` is a really useful program, but it can be tricky, can cause problems with docker eg.

## Install on Ubuntu 18.xx (or higher)

On **Ubuntu** - since **18.xx** version - `dnsmasq` won't be able to start! You have to reconfigure the NetworkManager!

> Source of the solution: https://askubuntu.com/a/1156952/1020961

```shell
# Disable systemd-resolved
$ sudo systemctl disable systemd-resolved.service
$ sudo systemctl stop systemd-resolved.service
$ sudo rm /etc/resolv.conf

# Reconfigure the NetworkManager, set dnsmasq
#
#   [main]
#   dns=dnsmasq
#
$ sudo sed -i '/^\[main\]/a dns=dnsmasq' /etc/NetworkManager/NetworkManager.conf

# Configure dnsmasq
$ echo "address=/loc/127.0.0.1" | sudo tee /etc/NetworkManager/dnsmasq.d/loc-tld

# and finally restart NetworkManager which will generate a new /etc/resolv.conf
$ sudo systemctl start dnsmasq.service
$ sudo systemctl restart network-manager.service
```

## Fix docker

First of all check you need or not for this fix:

```shell
$ docker run --rm busybox ping archive.ubuntu.com
```

If it is working you don't need this fix!

> The solution is based on: https://stackoverflow.com/a/50001940/1476819 and https://development.robinwinslow.uk/2016/06/23/fix-docker-networking-dns/

Dnsmasq can ruin the behavior of docker network, eg. you can get this error message during building: `Could not resolve 'archive.ubuntu.com'`. There are lot of wrong or half solution on the internet. I tested and use this:

```shell
# Configure dnsmasq
$ echo "listen-address=172.17.0.1" | sudo tee /etc/NetworkManager/dnsmasq.d/docker-bridge.conf
# Restart NetworkManager
$ sudo systemctl restart network-manager.service

# On the host, find out the primary and secondary DNS server addresses:
$ nmcli dev show | grep 'IP4.DNS'
IP4.DNS[1]:              10.0.0.2
IP4.DNS[2]:              10.0.0.3

# Configure docker with Google DNS fall back IPs, so you have to add the IPs from above:
$ cat /etc/docker/daemon.json   
{
  "dns": [
        "172.17.0.1",
        "10.0.0.2",
        "10.0.0.3",
        "8.8.8.8",
        "8.8.4.4"
  ]
}
# Restart docker
sudo systemctl restart docker.service

# Check
$ docker run --rm busybox ping archive.ubuntu.com
```
