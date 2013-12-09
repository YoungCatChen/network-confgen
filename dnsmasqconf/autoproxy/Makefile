_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS       := $(call addfix,$Pdns.,.txt,$(autoproxy-rules))

ifeq ($(call tobool,$(dnsmasqconf_autoproxy_enabled)),1)
TARGETS         += $(MYTARGETS)
JUNKS           += $Pdns.*.txt*
INSTALLS        += $P_install
UNINSTALL_JUNKS += $(dnsmasq_conf)/dns.*.txt
NEEDED_BINS     += awk
endif

$(MYTARGETS) : P := $P
$(MYTARGETS) : export PREFIX := dnsmasqconf_autoproxy_server
$(MYTARGETS) : $Pdns.%.txt : $$(apru_%) $P../../merge-domains $PgenerateServers $(MAIN_CONF)
	$P../../merge-domains $(filter-out %merge-domains %generateServers,$(call inputs-noconf)) > $@.mergeddom
	$PgenerateServers $@.mergeddom > $@~
	rm -f $@.mergeddom
	mv -f $@~ $@

$P_install : $(call addfix,$Pdns.,.txt,$(dnsmasqconf_autoproxy_install_ap_domains)) | $(dnsmasq_conf)
	install -m 644 $^ $(dnsmasq_conf)/
