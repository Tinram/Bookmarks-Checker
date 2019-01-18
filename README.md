
# Bookmarks Checker

#### Identify dead links in Firefox and Chrome bookmarks.


## Background

So many browser bookmarks &ndash; there are 1,800 URLs in my bookmarks. And in just one year, 120 of those URLs ceased to exist.

A simple PHP prototype script provided a slow way (~1 URL per second) of checking for dead links.

I switched to Python to leverage its threading capabilities and speed up the process.

... and then finally got round to adding cURL multi to the PHP original.


## Scripts

+ Python 3
+ Python 2
+ PHP and cURL


## Usage

[**Export browser bookmarks**](#export).

The scripts by default will attempt to load a file in the same directory called *bookmarks.html*

An alternative filename can be specified on the command-line.

The scripts parse the file and try to access each URL, printing a list of URLs that cannot be accessed (which will include some false positives compared to loading each URL in a browser).

### Python

```bash
    python3 bookmarks_checker.py

    python bookmarks_checker_py2.py
```

(or make the file executable and run directly e.g. `./bookmarks_checker.py`)

#### Switches

`-h` or `--help` displays help text.

`-f <file>` allows an alternatively-named file to be loaded instead of the default *bookmarks.html*

### PHP

```bash
    php bookmarks_checker.php [file]
````

## Exporting Browser Bookmarks <a id="export"></a>

### Firefox

*Bookmarks > Show All Bookmarks > Import and Backup > Export Bookmarks to HTML*

### Chrome

Access Chrome's *Bookmark Manager* with:

<kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>O</kbd>

or

`chrome://bookmarks/`

then click *Organize* > *Export bookmarks to HTML file ...*


## Other

### Python Scripts

Setting `DEBUG = True` will show all URLs as access is attempted, and either the successful response or the failure error message.


## Credits

Doug Hellmann, jfs, and philshem for threading pools in Python.


## License

Scripts are released under the [GPL v.3](https://www.gnu.org/licenses/gpl-3.0.html).
