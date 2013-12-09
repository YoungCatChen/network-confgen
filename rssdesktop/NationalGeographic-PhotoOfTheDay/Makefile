_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

N := NationalGeographic-PhotoOfTheDay

ifeq ($(call tobool,$(rssdesktop_enable_NationalGeographic-PhotoOfTheDay)),1)
NEEDED_BINS += php wget
TARGETS     += $P$N.theme.in $P$N.rss.in $Pimglist.txt
DOWNLOADS   += $P_rss
JUNKS       += $PPAGE* $Perrflag.tmp
endif


$P_rss : export P := $P
$P_rss : X := $Prss.down
$P_rss : Y := $PsubPageMap.txt
$P_rss :
	$(wget) -O $X~ \
		'http://feeds.nationalgeographic.com/ng/photography/photo-of-the-day/' && \
		mv -f $X~ $X
	$PdownloadSubPages $X > $Y~ && \
		mv -f $Y~ $Y

$Pimglist.txt : $PsubPageMap.txt
	sed 's/^.*  @@@  //' $< > $@~
	mv -f $@~ $@

$P$N.rss.in : export P := $P
$P$N.rss.in : $PreplaceRssEnclosure.php $Prss.down $PsubPageMap.txt
	$(php) $PreplaceRssEnclosure.php $Prss.down $PsubPageMap.txt > $@~
	mv -f $@~ $@

$P$N.theme.in : $(BINGTHEME)
	sed -r 's%^(DisplayName=)[^\r]*%\1National Geographic Photo of the Day @ {$$HOST}%' $< > $@~
	mv -f $@~ $@

N :=
