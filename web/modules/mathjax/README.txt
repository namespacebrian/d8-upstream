This module integrates the MathJax library into your Drupal site. MathJax is the
modern JavaScript-based LaTeX rendering solution for the Internet.

By default, MathJax source is loaded automatically from the Internet using the 
MathJax Content Delivery Network (CDN).

There are two MathJax configuration options: Text format (recommended), or 
Custom.

If you select "Text format", MathJax will be available as a text filter.
Mathematics inside the default delimiters will be rendered by MathJax. The
default math delimiters are $$...$$ and \[...\] for displayed mathematics, and
$...$ and \(...\) for in-line mathematics. You must add the MathJax filter to a 
text format and put MathJax at the bottom of the filter processing order.

You may select "Custom" if you need a more specific configuration. "Custom" is
the default when upgrading.

INSTALLATION
============

Using the MathJax CDN (recommended)
-------------------------------

1. Install and enable this module.

2. Add the MathJax filter to an existing or new text format under 
   Administration >> Configuration >> Text Formats. Put the MathJax filter at
   the bottom of the "Filter processing order".

3. Test it by adding a LaTeX formula between '$' in any node body (for example: 
   $2 + 2 = 4$). Select the body text format you configured on the Text Formats
   administration screen.


Using a local copy of MathJax
-----------------------------

1. Install and enable this module.

2. Install the third-party MathJax software:
     Download MathJax source from the MathJax website.
     Un-archive it into your "libraries" directory.
     You may need to create the "libraries" directory first.
     Rename it to "MathJax". When finished you should have this structure:
     `/libraries/MathJax/MathJax.js`

3. Follow from step #2 above.

ORIGINAL AUTHOR
===============
Module written by Thomas Julou.
http://drupal.org/user/273952

MAINTAINER(S)
=============
2013: Chris McCafferty (cilefen) https://drupal.org/u/cilefen
2014: P. Magunia (pmagunia) https://www.drupal.org/u/pmagunia

LEGAL
=====
MathJax CDN services are provided subject to its Terms of Service (TOS). By
accessing and using the MathJax CDN, you accept and agree to be bound by the
terms and provisions of the TOS:
https://www.mathjax.org/mathjax-cdn-terms-of-service/
