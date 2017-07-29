#!/bin/bash

# -------------------------------------------------
# This script should have only SIMPLE tests, and not
# meant to be comprehensive. The unit tests should
# consider detailed test cases. This is a smoke test
# and if you encounter an issue during these tests,
# you should re-examine your unit tests and whether
# there is a pattern of oversight in how tests are
# being selected.
# -------------------------------------------------

URL=`php -r '$e = include("./config.php"); echo $e["baseUrl"];'`

check() { grep -ie "$1" out.html > /dev/null; if [ $? = 1 ]; then echo ""; echo "Unable to find \"$1\" in output"; exit -1; else echo -n '.'; fi }
checknot() { grep -ie "$1" out.html > /dev/null; if [ $? != 1 ]; then echo ""; echo "Not expecting \"$1\" in output"; exit -1; else echo -n '.'; fi }
alwayscheck() { check "<html"; check "</html>"; checknot "exception"; }

browse() { rm -f out.html; echo ""; echo "Browsing to: $1"; curl -c cookies.txt -L -s -o out.html $1; alwayscheck; }
post() { rm -f out.html; echo ""; echo "Submitting form with $2 to: $1"; curl -c cookies.txt -L -s -o out.html -d $2 $1; alwayscheck; }
cleanup() { rm -f out.html; rm cookies.txt; }

# -------------------------------------------------
# Test cases
# -------------------------------------------------

rand=$RANDOM$RANDOM

homepage() 
{
    browse "$URL/";
    check 'button'
    check 'signup'
    check 'login'
}

signupAndLogin()
{
    browse "$URL/user/signup"
    check 'button'

    post "$URL/user/signup-submit" "email=a%2B$rand@b.com&password=pass123";
    check 'Confirmation email has been sent. Please check your email to login.'

    browse "$URL/user/login";
    check 'button'
    check 'login-submit'

    post "$URL/user/login-submit" "email=a%2B$rand@b.com&password=pass123";
    check 'needs verification'

    post "$URL/user/login-submit" "email=a@b.com&password=pass123";
    check 'logged in as'
}

biz()
{
    browse "$URL/biz/list"
    check 'button'
}

#set -x
homepage
signupAndLogin
biz
cleanup

echo ""
echo "All tests completed successfully!"
