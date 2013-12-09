_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

ifeq ($(call tobool,$(iptables_dual-routes_enabled)),1)
TARGETS         += $Pdual.iptables
JUNKS           += $Pdual.iptables* $Puninstall.iptables
INSTALLS        += $P_install
UNINSTALL_RULES += $P_uninstall
NEEDED_BINS     += awk gawk ip iptables-save iptables-restore
endif

# We use magic numbers 49 and 50 because the ascii codes of '1' and '2' are 49 and 50.
# 49 indicates the first route, which is the default one.
# And 50 indicates the alternative route, which is set in the CONF.

ifeq ($(iptables_dual-routes_route_by),gateway)
	iprule := $Pinstall-gateway
else ifeq ($(iptables_dual-routes_route_by),eth)
	iprule := $Pinstall-eth
else
	iprule := $Pinstall-invalid-iprule
endif

ifeq ($(iptables_dual-routes_invert_route),)
	X := 50
else
	X := 49
endif

$P_install : $Pdual.iptables $P_uninstall $(iprule)
	$(iptables-restore) -n $<

$P_uninstall : export P := $P
$P_uninstall : $Puninstall.awk
	$(iptables-save) -t mangle | $(gawk) -f $Puninstall.awk > $Puninstall.iptables
	$(iptables-restore) -n $Puninstall.iptables
	$(ip) route flush table 49
	$(ip) route flush table 50
	$(ip) rule del prio 4949 || true
	$(ip) rule del prio 5050 || true

$Pinstall-gateway :
	$(ip) rule add fwmark 0x$X$X table $X prio $X$X
	$(ip) route add default via '$(iptables_dual-routes_gateway)' table $X
$Pinstall-eth :
	$(ip) rule add fwmark 0x$X$X table $X prio $X$X
	$(ip) route add default eth $(iptables_dual-routes_eth) table $X
$Pinstall-invalid-iprule :
	@echo "Edit '$(MAIN_CONF)' and make sure the item 'route_by' is set to a valid value."
	@false


$Pdual.iptables : export P := $P
$Pdual.iptables : D := $(call extract-vars,iprg_,,$(iptables_dual-routes_always_default_ip_ranges))
$Pdual.iptables : F := $(call extract-vars,iprg_,,$(iptables_dual-routes_ip_ranges))
$Pdual.iptables : $(MAIN_CONF) $PMakefile $Pmark.awk $D $F
	{ echo '*mangle'; \
		echo '-N DUALPRE'; \
		$(awk) -f $Pmark.awk -v 'mark=0x5050' \
			$(if $F,$F,/dev/null); \
		echo '-A DUALPRE -m mark ! --mark 0x5050 -j MARK --set-mark 0x4949'; \
		$(awk) -f $Pmark.awk -v 'mark=0x0' \
			$(if $D,$D,/dev/null); \
		echo $(if $(subst 0,,$(iptables_dual-routes_enable_local)),'-I OUTPUT 1 -j DUALPRE',); \
		echo $(if $(subst 0,,$(iptables_dual-routes_enable_forward)),'-I PREROUTING 1 -j DUALPRE',); \
		echo 'COMMIT'; \
	} > $@~
	mv -f $@~ $@

iprule :=
