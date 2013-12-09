_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS   := $(call addfix,$Phosts.,.txt,$(hosts_include))

ifeq ($(call tobool,$(hosts_enabled)),1)
TARGETS     += $(MYTARGETS)
JUNKS       += $Phosts.*.txt*
INSTALLS    += $P_install
NEEDED_BINS += awk
endif

$(MYTARGETS) : $Phosts.%.txt : $$(hosts_%) $(MAIN_CONF)
	{ echo '####---- $* ----####'; \
		cat $(call inputs-noconf); \
		} > $@~
	mv -f $@~ $@

$P_install : MYTARGETS := $(MYTARGETS)
$P_install : $P../strip-generated $(MYTARGETS)
	$(awk) -f $< /etc/hosts > /tmp/hosts.txt
	{ cat /tmp/hosts.txt; \
		echo '####==== Generated start ====####'; \
		cat $(MYTARGETS); \
		echo '####==== Generated end ====####'; \
		} > /etc/hosts
	rm -f /tmp/hosts.txt

MYTARGETS :=
