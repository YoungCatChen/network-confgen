_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

$(call import,$(wildcard $P*/Makefile))

Q := $P
include $Pmainconf.mk
P := $Q
Q :=

MAIN_CONF     := $Pmainconf.mk
TARGETS       += $Pmainconf.mk
JUNKS         += $P*.mk*
NEEDED_BINS   += php

ALL_INIS      := $(wildcard $P../CONF/mainconf/*.ini)
ALL_AP_RULES  := $(wildcard $P../CONF/autoproxy-rules/*/)
ALL_IP_RANGES := $(wildcard $P../CONF/ip-ranges/*/)
ALL_HOSTSES   := $(wildcard $P../CONF/hostses/*/)


$Pmainconf.mk : export P := $P
$Pmainconf.mk : $Pini.mk $Pap.mk $Pip.mk $Phosts.mk
	{ head -n3 $PMakefile; \
		cat $^; \
		} > $@

$Pini.mk : export P := $P
$Pini.mk : $(ALL_INIS) $P../common.php
	$(if $(php),$(php),php) $P../common.php run inis2mk $(ALL_INIS) > $@~
	mv -f $@~ $@

$Pip.mk : export P := $P
$Pip.mk : $(ALL_IP_RANGES) $PMakefile
	( cd $P../CONF/ip-ranges && \
		echo 'X := $$P../CONF/ip-ranges' && \
		for i in *; do \
			IP4="$$IP4 $${i}4"; \
			IP6="$$IP6 $${i}6"; \
			echo 'export iprg_'$$i'4 := $$(abspath $$(wildcard $$X/'$$i'/*.ipv4))'; \
			echo 'export iprg_'$$i'6 := $$(abspath $$(wildcard $$X/'$$i'/*.ipv6))'; \
		done; \
		echo "export ip4-ranges := $$IP4"; \
		echo "export ip6-ranges := $$IP6"; \
		) > $@~
	mv -f $@~ $@

$Pap.mk : export P := $P
$Pap.mk : $(ALL_AP_RULES) $PMakefile
	( cd $P../CONF/autoproxy-rules && \
		echo 'X := $$P../CONF/autoproxy-rules' && \
		for i in *; do \
			echo 'export apru_'$$i' := $$(abspath $$(wildcard $$X/'$$i'/*.txt))'; \
		done; \
		echo 'export autoproxy-rules :=' *; \
		) > $@~
	mv -f $@~ $@

$Phosts.mk : export P := $P
$Phosts.mk : $(ALL_HOSTSES) $PMakefile
	( cd $P../CONF/hostses && \
		echo 'X := $$P../CONF/hostses' && \
		for i in *; do \
			echo 'export hosts_'$$i' := $$(abspath $$(wildcard $$X/'$$i'/*.txt))'; \
		done; \
		echo 'export hostses :=' *; \
		) > $@~
	mv -f $@~ $@

