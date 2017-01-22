var fs = require('fs');

var assert = require('assert'),
    test = require('selenium-webdriver/testing'),
    webdriver = require('selenium-webdriver');
 
function writeScreenshot(data, name, driver, done) {
    name = name || 'ss.png';
    var screenshotPath = '/tmp/';
    fs.writeFileSync(screenshotPath + name, data, 'base64');
    driver.quit();
    setTimeout(done, 8000);
};
 
test.describe('Vapid Space', function() {
	
	this.timeout(16000);

    test.it('should show home page', function(done) {
 
        var driver = new webdriver.Builder()
            .withCapabilities(webdriver.Capabilities.phantomjs())
            .build();
 
        driver.get('http://www.vapidspace.com');
 
        driver.takeScreenshot().then(function(data) {
            writeScreenshot(data, 'out1.png', driver, done);
        });
 
        //driver.quit();
    });
});
