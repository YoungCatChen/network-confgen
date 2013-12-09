_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

$(call import,$(wildcard $P*/Makefile))

