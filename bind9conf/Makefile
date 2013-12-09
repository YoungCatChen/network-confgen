_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS    := $(call addfix,$Pnamed.conf.,,$(ip4-ranges))

ifeq ($(call tobool,$(bind9conf_enabled)),1)
TARGETS      += $(MYTARGETS)
JUNKS        += $Pnamed.conf.*
NEEDED_BINS  += awk
endif


$(MYTARGETS) : $Pnamed.conf.% : $$(iprg_%) $(MAIN_CONF)
	{ echo 'acl "$*" {'; \
		$(awk) '{print $$1 "/" $$2 ";"}' \
		$(call inputs-noconf); \
		echo '};'; \
		} > $@~
	mv -f $@~ $@

MYTARGETS :=
