#!/bin/bash

if [[ $(hostname) == "WebServerVM" ]]; then
	set -x
	sudo tc qdisc del dev eth0 root
	sudo tc qdisc add dev eth0 root handle 1:0 tbf rate 7mbit latency 75ms burst 1540
	sudo tc qdisc add dev eth0 parent 1:0 netem delay 75ms
	set +x
fi

if [[ $(hostname) == "ClientVM" ]]; then
	set -x
	sudo tc qdisc del dev enp0s3 root
	sudo tc qdisc add dev enp0s3 root handle 1:0 tbf rate 750kbit latency 75ms burst 1540
	sudo tc qdisc add dev enp0s3 parent 1:0 netem delay 75ms
	set +x
fi
