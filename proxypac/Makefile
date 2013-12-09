_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

RESULTDIR  := $Pproxy.php.data
WWWDATADIR := $(www_dir)/proxy.php.data

ifeq ($(call tobool,$(proxypac_enabled)),1)
TARGETS            += $(RESULTDIR)/1.php $(RESULTDIR)/2.php
JUNKS              += $(RESULTDIR)/1.php* $(RESULTDIR)/2.php*
NEEDED_BINS        += gawk php

INSTALLS           += $(www_dir)/proxy.php \
                      $(WWWDATADIR)/index.html \
                      $(WWWDATADIR)/1.php \
                      $(WWWDATADIR)/2.php

UNINSTALL_JUNKS    += $(www_dir)/proxy.php
UNINSTALL_JUNKDIRS += $(WWWDATADIR)
endif


$Pproxy.txt :
	@echo '# Example:  myhost  10.234.1.2:8080' > $@
	@echo "Edit file '$@' to meet your needs."


$(RESULTDIR)/1.php : export P := $P
$(RESULTDIR)/1.php : $Pproxy.txt $Ppredefproxy.awk
	{ echo "// <?php die; ?>"; \
		echo '// v''im: set ft=javascript:'; \
		$(gawk) -f $Ppredefproxy.awk $<; \
		} > $@~
	mv -f $@~ $@


directip4 = $(iprg_interfacelocal4) $(iprg_linklocal4)
directip6 = $(iprg_interfacelocal6) $(iprg_linklocal6)


$(RESULTDIR)/2.php : export P := $P
$(RESULTDIR)/2.php : $Pgen-pac-2-in $Pautoproxy.awk $Pfreeip.awk \
	$(directip4) $(directip6) \
	$(iprg_pkufreeip4) $(iprg_pkuip4) $(iprg_pkuip6) \
	$(iprg_privatenetwork4) $(iprg_privatenetwork6) \
	$(apru_gfwlist)
	$Pgen-pac-2-in \
		$(words $(apru_gfwlist)) $(apru_gfwlist) \
		$(words $(directip4)) $(directip4) \
		$(words $(directip6)) $(directip6) \
		$(words $(iprg_pkufreeip4)) $(iprg_pkufreeip4) \
		$(words $(iprg_pkuip4)) $(iprg_pkuip4) \
		$(words $(iprg_pkuip6)) $(iprg_pkuip6) \
		> $@~
	mv -f $@~ $@

$(www_dir)/proxy.php $(WWWDATADIR)/index.html $(WWWDATADIR)/1.php $(WWWDATADIR)/2.php : \
	$(www_dir)/% : $P% | $(WWWDATADIR)
	install -m 644 $< $@

$(WWWDATADIR) : $(MAIN_CONF)
	install -m 755 -d $@

RESULTDIR :=
WWWDATADIR :=
