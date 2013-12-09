_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS       := $(call addfix,$Papdm.,.txt,$(autoproxy-rules))

ifeq ($(call tobool,$(squidconf_autoproxy_enabled)),1)
TARGETS         += $(MYTARGETS)
JUNKS           += $Papdm.*.txt*
INSTALLS        += $P_install
UNINSTALL_JUNKS += $(squid_conf)/apdm.*.txt
NEEDED_BINS     += awk
endif

$(MYTARGETS) : P := $P
$(MYTARGETS) : $Papdm.%.txt : $$(apru_%) $P../../merge-domains $(MAIN_CONF)
	$P../../merge-domains $(filter-out %merge-domains,$(call inputs-noconf)) | sed 's%[^ ]%.\0%' > $@~
	mv -f $@~ $@

$P_install : $(call addfix,$Papdm.,.txt,$(squidconf_autoproxy_install_ap_domains)) | $(squid_conf)
	install -m 644 $^ $(squid_conf)/
