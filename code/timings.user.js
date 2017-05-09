// ==UserScript==
// @name         Timings
// @namespace    namespace
// @version      0.123
// @description  description
// @author       Jens Forstmann
// @match        *://*/*
// @grant        none
// ==/UserScript==

var version = "0.123";

console.log("timings userscript load, " + version);

function pad(num, size) {
	var s = num+"";
	while (s.length < size) s = "0" + s;
	return s;
}

function download(filename, text) {
	var element = document.createElement('a');
	element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
	element.setAttribute('download', filename);

	element.style.display = 'none';
	element.setAttribute('id', 'timid');
	document.body.appendChild(element);

	element.click();

	//document.body.removeChild(element);
}

// window.onload = function() {
var _timings = function() {
	console.log("_timings start");
	var resourceList = window.performance.getEntriesByType("resource");
	var x = {};
	x.host = window.location.host;
	x.pathname = window.location.pathname;
	x.protocol = window.location.protocol;
	x.search = window.location.search;
	x.hash = window.location.hash;
	x.href = window.location.href;
	var jsonString = JSON.stringify({info: x, data: resourceList}, null, "  ");
	console.log(jsonString);
	
	var d = new Date();
	
	var filename = x.protocol.substr(0, x.protocol.length - 1) + "_" +
		x.host + "_" +
		d.getFullYear() + "-" + pad(d.getMonth() + 1, 2) + "-" + pad(d.getDate(), 2) + "_" +
		pad(d.getHours(), 2) + "-" + pad(d.getMinutes(), 2) + "-" + pad(d.getSeconds(), 2) + "_" +
		x.hash.substr(1) +
		".txt";
	
	download(filename, jsonString);
	
	console.log("_timings end");
};

window.addEventListener("load", _timings, false);
