
# Bookmarks Checker


#### Check Firefox and Chrome bookmarks for dead links.


## Background

So many browser bookmarks!

There are 1,800 URLs in my bookmarks. In just one year, 120 of those URLs ceased to exist.

A simple PHP script provided a somewhat slow way (~1 URL per second) of checking for dead links.

I switched to Python to see if its threading capabilities could speed up the process.


## Scripts

+ Python 3
+ Python 2
+ PHP


## Usage

[**Export browser bookmarks**](#export).

The scripts by default will attempt to load a file called *bookmarks.html*  An alternative filename can be specified on the command-line.

The scripts will parse the file and test the URLs, displaying a list of URLs that cannot be accessed.

### Python

        python3 bookmarks_checker.py

        python bookmarks_checker_py2.py

(or make the file executable and run directly e.g. `./bookmarks_checker.py`)

#### Switches

`-h` or `--help` displays help text.

`-f <file>` allows an alternatively-named file to be loaded instead of the default *bookmarks.html*

### PHP

        php bookmarks_checker.php [file]


## Exporting Browser Bookmarks <a id="export"></a>

### Firefox

*Bookmarks > Show All Bookmarks > Import and Backup > Export Bookmarks to HTML*

### Chrome

Access Chrome's *Bookmark Manager* with:

        Ctrl + Shift + O

or

        chrome://bookmarks/

Then click *Organize* > *Export bookmarks to HTML file ...*


## Other

### Python Scripts

Setting `DEBUG = True` will show all URLs as access is attempted, and the successful response, or the failure error message.


## Credits

Doug Hellmann, jfs, and philshem for threading pools in Python.


## License

Scripts are released under the [GPL v.3](https://www.gnu.org/licenses/gpl-3.0.html).
