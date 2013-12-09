_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

ifeq ($(call tobool,$(squidconf_peer-select_enabled)),1)
TARGETS            += $Pconf.exported
JUNKS              += $Pconf.exported
INSTALLS           += $P_install
UNINSTALL_JUNKDIRS += $(squid_conf)/peer-select
NEEDED_BINS        += php squid
endif

$Pconf.exported : $PgenerateConfig.php $(MAIN_CONF) $(ALL_INIS)
	$(php) $< $(ALL_INIS) > $@~
	mv -f $@~ $@

$P_install : export P := $P
$P_install : $Pselect.php $Pconf.exported
	install -m 755 -d $(squid_conf)/peer-select
	install -m 644 $Pselect.php $Pconf.exported $(squid_conf)/peer-select/
	$(php) $(squid_conf)/peer-select/select.php pick

