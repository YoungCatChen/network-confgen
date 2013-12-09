_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS       := $(call addfix,$Piprg.,.txt,$(ip4-ranges))
MYTARGETS       += $(call addfix,$Piprg.,.txt,$(ip6-ranges))

ifeq ($(call tobool,$(squidconf_ip-ranges_enabled)),1)
TARGETS         += $(MYTARGETS)
JUNKS           += $Piprg.*.txt*
INSTALLS        += $P_install
UNINSTALL_JUNKS += $(squid_conf)/iprg.*.txt
NEEDED_BINS     += awk
endif

$(MYTARGETS) : $Piprg.%.txt : $$(iprg_%) $(MAIN_CONF)
	$(awk) '/^[^#]/ && $$2~/./ {print $$1 "/" $$2}' \
		$(call inputs-noconf) \
		> $@~
	mv -f $@~ $@

$P_install : $(call addfix,$Piprg.,.txt,$(squidconf_ip-ranges_install_ip_ranges)) | $(squid_conf)
	install -m 644 $^ $(squid_conf)/
