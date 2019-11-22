# misp-test

This is a very simple php script to try and get to the ground of various aspects of [MISP](https://github.com/MISP/MISP) during the installation and other maintenance tasks.

# Usage

```bash
# For Ubuntu
TEST_PHP="test_$(echo $RANDOM|shasum|cut -f1 -d\ ).php"
echo "https://localhost/${TEST_PHP}"
sudo -u www-data wget --no-cache -O /var/www/MISP/app/webroot/${TEST_PHP} https://raw.githubusercontent.com/SteveClement/misp-test/master/test.php
# For CentOS/RHEL
sudo -u apache wget --no-cache -O /var/www/MISP/app/webroot/${TEST_PHP} https://raw.githubusercontent.com/SteveClement/misp-test/master/test.php
```

