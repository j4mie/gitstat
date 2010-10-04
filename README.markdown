Gitstat
=======

Display a nice webpage showing the status of a bunch of git repositories.

Good for shared staging servers and stuff. I'm sure that you don't need this if you have a sensible deployment strategy. But some people don't.

<img src="http://img.skitch.com/20101004-tbqw1qy58kemuws7sphbceasy9.png" />

1. Copy the `config_sample.ini` file to `config.ini`.
2. Copy the example repository block as many times as you need. Make sure you leave the `[General]` block in there.
3. Set up a `cron` job to run `php gitstat.php` every ten minutes or so.
4. Make the directory servable. You probably want to be careful to not expose this to the outside world, as requesting 
   `gitstat.php` repeatedly would be a fairly effective DOS attack. Stick a suitable `.htaccess` file in there or something.
5. Go to `index.html` in your browser. Hopefully you'll see a nice screen like the one above.

**Note**: this was a quick hack written at midnight on a sofa at [BarCampBrighton5](http://www.barcampbrighton.org). Use at your own risk etc.
