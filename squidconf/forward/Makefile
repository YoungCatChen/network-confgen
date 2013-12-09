_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

www_php            := $(www)/forward.php
www_data           := $(www_php).data
MYTARGETS          := $Pbuilt/config.mk $Pbuilt/squid-ver.mk $Pbuilt/squid-ver.txt \
                      $Pbuilt/php-mhash.txt $Pbuilt/php-pcntl.txt $Pbuilt/reload-squid

ifeq ($(call tobool,$(squidconf_forward_enabled)),1)
TARGETS            += $Pbuilt $(MYTARGETS)
JUNKDIRS           += $Pbuilt
INSTALLS           += $P_install
UNINSTALL_JUNKS    += $(www_php)
UNINSTALL_JUNKDIRS += $(www_data) $(squid_conf)/forward
NEEDED_BINS        += awk php squid cc
endif

$Pbuilt :
	mkdir -p $@

$Pbuilt/config.mk : $PgenerateConfig.php $(MAIN_CONF) $(ALL_INIS)
	$(php) $< $(ALL_INIS) > $@~
	mv -f $@~ $@

$Pbuilt/squid-ver.mk : $PdetectVersion.php $(MAIN_CONF)
	SQUID=$(squid) $(php) $< > $@~
	$(awk) '{print "version_major =", $$1, "\nversion_minor =", $$2; }' $@~ > $@

$Pbuilt/squid-ver.txt : $Pbuilt/squid-ver.mk
	$(eval include $<)
	touch $@

$Pbuilt/php-mhash.txt : $(MAIN_CONF)
	@$(php) -r '$$a=mhash(MHASH_CRC32, "a");' || { \
		echo "Your php does not support 'mhash'. Please install mhash for php first." >&2; \
		false; }
	touch $@

$Pbuilt/php-pcntl.txt : $(MAIN_CONF)
	@$(php) -r 'pcntl_fork();' || { \
		echo "Your php does not support PCNTL functions. Please reconfigure your php first." >&2; \
		false; }
	touch $@

$Pbuilt/reload-squid : $(MAIN_CONF) $PMakefile
	{ echo '#include <unistd.h>'; \
		echo 'int main() { return -(setuid(0) || setgid(0) ||' \
		'execlp("$(squid)", "$(squid)",' \
		'"-f", "$(squid_conf_file)", "-k", "reconfigure", NULL)); } '; \
		} | $(cc) -x c -o $@ -


$P_install : export P := $P
$P_install : www_php := $(www_php)
$P_install : www_data := $(www_data)
$P_install : $(MAIN_CONF) $Pforward.php.in $Pforward.php.data/* $(MYTARGETS)
	install -m 755 -o $(squidconf_forward_www_euser) -g $(squidconf_forward_www_egroup) -d $(www_data)
	install -m 755 -o $(squidconf_forward_www_euser) -g $(squidconf_forward_www_egroup) -d $(squid_conf)/forward
	install -m 644 $Pforward.php.data/* $(www_data)/
	$(php) $Pforward.php.in run replaceRequire $Pforward.php.in \
		| install -m 644 /dev/stdin $(www_php)
	{ echo "; <?php die; ?>"; \
		sed 's/#/;/' $Pbuilt/squid-ver.mk $Pbuilt/config.mk; \
		} | install -m 644 /dev/stdin $(www_data)/config.ini.php
	install -m 6755 $Pbuilt/reload-squid $(squid_conf)/
	su $(squidconf_forward_www_euser) -c '$(php) $(www_php) make'

www_php :=
www_data :=
MYTARGETS :=
