_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS   := $(abspath $P../../CONF/hostses/ipv6hosts/10-ipv6hosts.txt)
ENCODED     := $Pgooglehosts.encoded.txt
DOWNLOADS   += $P_download
JUNKS       += $Pgooglehosts.*txt* $(MYTARGETS)
NEEDED_BINS += wget

$(if $(wildcard $(MYTARGETS)),,$(shell touch -t 197001010800 '$(MYTARGETS)'))

$P_download : ENCODED := $(ENCODED)
$P_download :
	$(wget) -O $(ENCODED)~ \
		'https://ipv6-hosts.googlecode.com/hg/hosts' && \
		mv -f $(ENCODED)~ $(ENCODED)

$(MYTARGETS) : $(ENCODED)
	ex '+e ++enc=utf8 $<' '+set endofline' '+w! $@' '+q'

ENCODED :=
MYTARGETS :=

