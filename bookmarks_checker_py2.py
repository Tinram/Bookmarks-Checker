#!/usr/bin/env python
# -*- coding: utf-8 -*-

""" Bookmarks Checker """


import os
import sys
import re
import argparse
from time import time as timer
from multiprocessing.dummy import Pool as ThreadPool
from urllib2 import Request, urlopen


class BookmarksChecker(object):

    """
        Bookmarks Checker
        Verify links in a Chrome or Firefox exported bookmarks file.

        Usage              python bookmarks_checker_py2.py [-f file]

        Python Version     2.7
        Author             Martin Latter <copysense.co.uk>
        Copyright          Martin Latter 18/09/2017
        Version            0.03
        Credits            jfs and philshem (pool threading)
        License            GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        Link               https://github.com/Tinram/Bookmarks-Checker.git
    """


    DEBUG = False
    USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0'
    NUMBER_THREADS = 16


    def __init__(self):

        """ Initialise and execute methods. """

        filename = self.get_args()
        self.check_file(filename)
        self.parse_file(filename)


    def get_args(self):

        """ Parse the command line arguments. """

        parser = argparse.ArgumentParser()

        parser.add_argument(
            '-f', '--file',
            dest='filename',
            help='Specify filename of the bookmarks file to load',
            default='bookmarks.html',
            type=str,
            action='store')

        args = parser.parse_args()

        return args.filename


    def check_file(self, filename):

        """
            Check bookmark file existence and access.
            Args:
                filename: name of bookmarks file.
        """

        if not os.access(filename, os.R_OK):
            print '\n %s cannot be found or cannot be read.\n' % filename
            sys.exit(1)


    def parse_file(self, filename):

        """ Parse the file and extract links.
            Args:
                filename: name of bookmarks file.
        """

        urls = []
        url_index = {}
        link_counter = 0
        dead_link_counter = 0

        with open(filename) as bmfile:
            for line in bmfile:
                full_url = re.findall(r'(<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>)', line, re.I)
                if full_url:
                    urls.append(full_url[0][1])
                    url_index[full_url[0][1]] = full_url[0][2]

        if not len(urls):
            print '\n No links extracted from %s\n' % filename
            sys.exit(1)

        print '\n %i links being checked ...' % len(urls)

        start = timer()

        pool = ThreadPool(self.NUMBER_THREADS)
        results = pool.imap_unordered(self.check_url, urls)
        pool.close()
        pool.join()

        print '\n failures:\n'

        for url, error in results:

            url_name = url_index[url]

            if error is None:
                if not self.DEBUG:
                    pass
                else:
                    print ' ok: %s  |  %s' % (url_name, url)
            else:
                dead_link_counter += 1
                if not self.DEBUG:
                    print '\t %s  |  %s' % (url_name, url)
                else:
                    print ' F:  %s  |  %s -- %s' % (url_name, url, error)

            link_counter += 1

        print '\n %i links failed' % dead_link_counter
        print ' %i links verified' % (link_counter - dead_link_counter)
        print '\n URL parse time: %s secs\n' % str.format('{0:.5f}', (timer() - start))


    def check_url(self, url):

        """
            Check URL access.
            Args:
                url: a single URL.
        """

        headers = {'User-Agent': self.USER_AGENT}
        request = Request(url, headers=headers)

        try:
            response = urlopen(request)
            return url, None
        except Exception as err:
            return url, err

# end class



def main():

    """ Invoke class. """

    BookmarksChecker()


if __name__ == '__main__':

    main()
