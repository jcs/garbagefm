## Garbage CMS

This is the old source code to the CMS that ran the Garbage Podcast website.
The
[website](https://garbage.jcs.org/)
is now a static cache of the output from the CMS, so this code is no longer
being used or maintained but feel free to fork it and use it according to the
[license](LICENSE).

It is written in the
[Halfmoon PHP MVC Framework](https://github.com/jcs/halfmoon)
and uses MySQL for the database backend, but I no longer have the database
schema handy.
Apparently it was not using migrations for schema changes :(

The `bin/mp3chap` file is a binary of
[mp3chap](https://github.com/jcs/mp3chap),
statically compiled to work within a web chroot.
