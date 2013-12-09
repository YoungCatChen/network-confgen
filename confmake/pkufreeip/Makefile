_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

MYTARGETS   := $(abspath $P../../CONF/ip-ranges/pkufreeip/10-pkufree.ipv4)
DOWNLOADS   += $P_download
JUNKS       += $Pliebiao.txt* $(MYTARGETS)
NEEDED_BINS += wget gawk

$(if $(wildcard $(MYTARGETS)),,$(shell touch -t 197001010800 '$(MYTARGETS)'))

$P_download : export P := $P
$P_download :
	$(wget) -O $Pliebiao.txt~ \
		'http://its.pku.edu.cn/oper/liebiao.txt' && \
		mv -f $Pliebiao.txt~ $Pliebiao.txt

$(MYTARGETS) : export P := $P
$(MYTARGETS) : $Pliebiao.txt $Pparse.awk
	$(gawk) -f $Pparse.awk $< > $@~
	mv -f $@~ $@

MYTARGETS :=
