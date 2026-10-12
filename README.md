# SimpleMSPDoc RC 1.0

## Upcoming rebuild: compatibility notice

SimpleMSPDoc is being redesigned from scratch. **The upcoming rebuilt version will not be backward-compatible with previous versions and will require a fresh database. No migration or in-place upgrade from legacy versions will be provided.**

If you use a previous version, retain that installation and back up its database and uploaded files. Install the rebuilt version separately with a new database; do not overwrite your existing installation or point the rebuild at its database.

The rebuild is currently in design. The existing code and installation instructions below describe the legacy RC 1.0 application. See the [rebuild design](docs/superpowers/specs/2026-10-12-simplemspdoc-rebuild-design.md) for the proposed replacement.

#Feedback wanted before moving forward past 1.0. Please submit your feedback. Thanks.

I wasn't happy with what other IT documention software had.
I felt they over complicated things and required so much clicky clicky to get to anything.

This idea is ready for use. It is still prett basic/Simple with no plans to make it super crazy.

If you want to try it out:
1. download and put it on your webserver.
2. create a MySql database.
3. create a directory for file uploads (best is outside of the web accessable area).
4. Set permissions on file so the webserver will have read/write access to it.
5. configure config.php with the database information and pointing to your directory created in step 3.
6. navigate your webbrowser to the install.php file
7. follow the steps
8. remove the install.php file for security
9. then use.


Contribute.
If you want to help out in anyway, I welcome your assistance.

This is writting in PHP/MySQL

Find the project at https://github.com/flattery103/SimpleMSPDoc
