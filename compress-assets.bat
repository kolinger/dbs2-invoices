@echo off

type www\assets\js\modernizr-2.7.0.min.js^
	www\assets\js\jquery-1.10.2.min.js^
	www\assets\js\jquery-ui-1.10.4.js^
	www\assets\js\jquery-ui-timepicker-addon.js^
	www\assets\js\jquery.nette-ajax.js^
	www\assets\js\bootstrap.js^
	www\assets\js\functions.js^
	> www\assets\output\temp.js
java -jar "C:\Program Files\yui-compressor\yuicompressor.jar" www/assets/output/temp.js -o www/assets/output/scripts.js --charset utf8 --line-break 1000
rm www\assets\output\temp.js

type www\assets\css\reset.css^
	www\assets\css\jquery-ui-1.10.4.css^
	www\assets\css\bootstrap.css^
	www\assets\css\bootstrap-theme.css^
	www\assets\css\style.css^
	> www\assets\output\temp.css
java -jar "C:\Program Files\yui-compressor\yuicompressor.jar" www/assets/output/temp.css -o www/assets/output/styles.css --charset utf8 --line-break 1000
rm www\assets\output\temp.css

echo === DONE ===
pause
