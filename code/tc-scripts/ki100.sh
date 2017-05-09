#!/bin/bash

if [[ $(hostname) == "WebServerVM" ]]; then
	set -x
	sudo tc qdisc del dev eth0 root
	sudo tc qdisc add dev eth0 root handle 1:0 tbf rate 100mbit latency 15ms burst 1540
	sudo tc qdisc add dev eth0 parent 1:0 netem delay 15ms
	set +x
fi

if [[ $(hostname) == "ClientVM" ]]; then
	set -x
	sudo tc qdisc del dev enp0s3 root
	sudo tc qdisc add dev enp0s3 root handle 1:0 tbf rate 2500kbit latency 15ms burst 1540
	sudo tc qdisc add dev enp0s3 parent 1:0 netem delay 15ms
	set +x
fi
