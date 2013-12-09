_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

ifeq ($(call tobool,$(iptables_portmapping_enabled)),1)
TARGETS         += $Pmappings.iptables
JUNKS           += $Pmappings.iptables* $Puninstall.iptables
INSTALLS        += $P_install
UNINSTALL_RULES += $P_uninstall
NEEDED_BINS     += gawk iptables-save iptables-restore php
endif

$P_install : export P := $P
$P_install : $Pmappings.iptables $P_uninstall
	$(iptables-restore) -n $<

$P_uninstall : export P := $P
$P_uninstall :
	$(iptables-save) -t nat | $(gawk) -f $Puninstall.awk > $Puninstall.iptables
	$(iptables-restore) -n $Puninstall.iptables

$Pmappings.iptables : export P := $P
$Pmappings.iptables : $Pinstall.php $(MAIN_CONF)
	$(php) $< $(ALL_INIS) \
		> $@~
	mv -f $@~ $@

