_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

BINGRSS     := $PBingDynamic.rss.in
BINGTHEME   := $PBingDynamic.theme.in

NEEDED_BINS += php wget
TARGETS     += $(BINGRSS) $(BINGTHEME) $Pimglist.txt
DOWNLOADS   += $P_bingtheme $P_bingrss


$P_bingrss : X := $Prss.down
$P_bingrss :
	$(wget) -O $X~ \
		'http://themeserver.microsoft.com/default.aspx?p=Bing&c=Desktop&m=en-US' && \
		mv -f $X~ $X

$P_bingtheme : X := $Ptheme.down
$P_bingtheme :
	$(wget) -O $X~ \
		'http://download.microsoft.com/download/F/6/2/F623B1DF-2C2E-4E3D-BBFD-70B10657714C/BingDynamic1.theme' && \
		mv -f $X~ $X

$Pimglist.txt : $Prss.down
	egrep -o 'http:[^"<]*\.[jJ][pP][gG]' $< | uniq > $@~
	mv -f $@~ $@

$(BINGRSS) : $Prss.down
	sed -r -e 's%http:[^?]*/default.aspx%{$$BASE}/{$$RSS}%g' -e 's%http:[^"<]*/%{$$BASE}/images/%g' $< > $@~
	mv -f $@~ $@

$(BINGTHEME) : $Ptheme.down
	sed -r -e 's%^DisplayName=[^\r]*%\0 @ {$$HOST}%' -e 's%^(RSSFeed=)[^\r]*%\1{$$BASE}/{$$RSS}%' $< > $@~
	mv -f $@~ $@

