_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS   := $(abspath $P../../CONF/autoproxy-rules/gfwlist/10-gfwlist.txt)
ENCODED     := $Pgfwlist.encoded.txt
DOWNLOADS   += $P_download
JUNKS       += $Pgfwlist.*txt* $(MYTARGETS)
NEEDED_BINS += wget base64

$(if $(wildcard $(MYTARGETS)),,$(shell touch -t 197001010800 '$(MYTARGETS)'))

$P_download : ENCODED := $(ENCODED)
$P_download :
	$(wget) -O $(ENCODED)~ \
		'https://autoproxy-gfwlist.googlecode.com/svn/trunk/gfwlist.txt' && \
		mv -f $(ENCODED)~ $(ENCODED)

$(MYTARGETS) : $(ENCODED)
	$(base64) -d $< > $@~
	mv -f $@~ $@

ENCODED :=
MYTARGETS :=
