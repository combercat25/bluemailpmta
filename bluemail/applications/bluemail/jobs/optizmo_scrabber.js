/**
 * @framework       IRFramework 
 * @version         1.0
 * @author          iResponse
 * @date            2017
 * @name            optizmo_scrabber.js
 */

var casper = require('casper').create();
casper.userAgent("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.72 Safari/537.36");
var xpath = require('casper').selectXPath;
var fs = require('fs');
casper.start(casper.cli.args[0]);

casper.wait(3000,function(){
    casper.click(xpath('//input[contains(@class,"submit")][1]')); 
});

casper.waitForSelector('.textCenter', function ()
{
    var text = casper.getElementAttribute(xpath('//p[contains(@class,"textCenter")][1]/a'), 'href');
    this.echo(text);
}, function ()
{
    this.echo('No Link Found !');
}, 60000);

casper.then(function(){
    casper.exit();
});

casper.run();