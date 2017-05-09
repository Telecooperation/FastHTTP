#!/bin/bash

if [[ $(hostname) == "WebServerVM" ]]; then
	set -x
	sudo tc qdisc del dev eth0 root
	sudo tc qdisc add dev eth0 root handle 1:0 tbf rate 50mbit latency 12ms burst 1540
	sudo tc qdisc add dev eth0 parent 1:0 netem delay 12ms
	set +x
fi

if [[ $(hostname) == "ClientVM" ]]; then
	set -x
	sudo tc qdisc del dev enp0s3 root
	sudo tc qdisc add dev enp0s3 root handle 1:0 tbf rate 10mbit latency 12ms burst 1540
	sudo tc qdisc add dev enp0s3 parent 1:0 netem delay 12ms
	set +x
fi
