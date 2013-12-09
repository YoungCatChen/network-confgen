_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

DIRS := BingDynamic  NationalGeographic-PhotoOfTheDay

ifeq ($(call tobool,$(rssdesktop_enabled)),1)

$(call import,$(call addfix,$P,/Makefile,$(DIRS)))

TARGETS            += $Pwww/imglist.txt

JUNKS              += $(call addfix,$P,/*.down*,$(DIRS)) \
                      $(call addfix,$P,/*.in*,$(DIRS)) \
                      $(call addfix,$P,/imglist.txt,$(DIRS)) \
                      $Pwww/imglist.txt

INSTALLS           += $P_install
UNINSTALL_JUNKDIRS += $(www)/rssdesktop

endif


$Pwww/imglist.txt : $(call addfix,$P,/imglist.txt,$(DIRS))
	cat $^ > $@

$P_install : export P := $P
$P_install : DIRS := $(DIRS)
$P_install : rssdir := $(www)/rssdesktop
$P_install : imgdir := $(www)/rssdesktop/images
$P_install :
	install -m 755 -d $(rssdir)
	install -m 755 -o $(rssdesktop_www_euser) -g $(rssdesktop_www_egroup) -d $(imgdir)
	install -m 644 $Pwww/* $(rssdir)/
	install -m 644 $(call addfix,$P,/*.in,$(DIRS)) $(rssdir)/
	chmod +x $(rssdir)/download-images
	mv -f $(rssdir)/htaccess $(rssdir)/.htaccess
	install -m 644 $Pwwwimages/htaccess $(imgdir)/.htaccess
	[ -e $(imgdir)/lockex ] || install -m 644 /dev/null $(imgdir)/lockex
	$(php) $Pwwwimages/index.php.in run replaceRequire $Pwwwimages/index.php.in \
		| install -m 644 /dev/stdin $(imgdir)/index.php


DIRS :=
