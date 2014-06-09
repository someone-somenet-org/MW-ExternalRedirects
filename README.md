The ExternalRedirects extension allows you to use normal redirects to redirect
to external websites. It does this by checking if the target of the redirect
starts with one of the protocols defined in $wgExternalRedirectProtocols and
redirecting to it if a match is found. The extension is part of the
[PerPageResources-project](https://fs.fsinf.at/wiki/PerPageResources) but can
also be used standalone.

=== Download ===
Just do a normal

```
git clone https://github.com/mathiasertl/ExternalRedirects.git ExternalRedirects
```

Older versions are marked as tags, you can view available tags with git tag -l
and move back to the current HEAD with git checkout <tag-name>. Versions for
older versions of MediaWiki, if available, are managed as branches, use git
branch and git checkout <branch> to move to a specific branch. To move back to
the newest version, use git checkout master.

Note that GitHub allows you to download older different commits as tarball if
you do not want to install git. 

=== Installation & Configuration ===
Download ExternalRedirects.php and include these lines in LocalSettings.php.
```php
require_once("$IP/extensions/ExternalRedirects/ExternalRedirects.php");
$wgEnableExternalRedirects = true;
$wgExternalRedirectProtocols = array('https', 'http', 'ftp');
```

* **$wgEnableExternalRedirects:** A simple kill-switch in case this extension is
abused by spammers. $wgExternalRedirectProtocols allows you to configure what
protocols are detected as external redirects.
; **$wgExternalRedirectProtocols:** Defines which protocols are recognized. The
strings are actually used in a regular expression, so the above example would
be equivalent to

```php
$wgExternalRedirectProtocols = array('https?', 'ftp');
```

... which would make the 's' in "https" optional.


It is also recommended that you apply the [[HideExternalRedirects]]-patch so
that [[Special:BrokenRedirects]] does not list External Redirects.

=== Changelog ===
===== 1.5.5 =====
* Improve regex matching external redirects so URLs can include "()".
* Move repository to github.

===== 1.5.4 =====
* Remove use of deprecated functions
* Set a required property in special pages
* Code-style cleanup

===== 1.5.3 =====
* Update to work with new MediaWikis (requires 1.15.0 or later).
* Handle external redirects that don't include a page text more gracefully.
* This is the first version managed in git.

===== 1.5.2 =====
* Made regex that matches ExternalRedirects case-insensitive
* Some code-cleanup in ExternalRedirects.php

===== 1.5.1 =====
* Added [[Special:ExternalRedirects]]. 
* Introduced $wgExternalRedirectsEnableSpecialPage that controls if
  Special:ExternalRedirects is listed or not
  * Some internationalization-work for the special page
  : '''Note:''' This changelog is retroactive from SVN-changelogs.

===== 1.2.1 =====
* fixed a bug that caused broke external redirects with braces in their
linktext.
* all in all more elaborate regular expressions

===== 1.2 =====
* Display of external redirects is now way cleaner. Text is now big but still
shown as external redirect (--> image) and categories are shown as well.

===== 1.1 =====
* Refine matching of Redirects so they now accecpt categories.

===== 1.0 =====
* First version documented here.

=== Licence ===
[GPL v3](http://www.gnu.org/licenses/gpl-3.0.html) or any later version. 

