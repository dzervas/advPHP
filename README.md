# advPHP
A command line interface for advmame with ncurses

This is a piece of my museum. It's dated about 2010-2011.
I think advPHP is long dead but, who knows, someone out there
may be still running it

To run it you need PHP 5 with ncurses.
To run it in docker:
```
docker build -t advphp . && docker run -it --name advphp advphp; reset
docker start -ai advphp
```

Note: I'm doing a hack with docker 'cause the script has to run once to
generate some data and then re-run.

Enjoy my highschool code!
