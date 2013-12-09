# This is the ROOT Makefile, its name should be GNUmakefile, because we rely on GNU make 3.81+
__debug__ := y
default: all


# internal. don't touch :) {{{
# internal _get_dir: set the current directory to $P {{{
define __get_dir
 _last_makefile := $$(lastword $$(MAKEFILE_LIST))
 P:=$$(dir $$(_last_makefile))
endef
_get_dir = $(eval $(__get_dir))
# }}}
$(_get_dir) # should be the first (before any include statement)

# _is_main_makefile: save the flag whether we are the main Makefile {{{
_is_main_makefile := $(if $(subst $(firstword $(MAKEFILE_LIST)),,$(_last_makefile)),n,y)
# }}}

# Convenient constants {{{
comma   := ,
squote  := '# comment for vim's highlight syntax
empty   :=# end of line
space   := $(empty) $(empty)# end of line
define newline


endef
# }}}
# Global flags {{{
_accum_progs_ := y# if set to null then no more ALL_PROGS update
__indent__ :=# indent debug messages
export SHELLOPTS := $(patsubst :%,%,$(SHELLOPTS):errexit:pipefail)
# }}}
# debugwarn/debuginfo: debug output routine with proper indentation {{{
_warning = $(warning $(1))
_info = $(info $(1))
define debugwarn
$(if $(subst n,,$(__debug__)),$(call _warning,$(__indent__)$(1)$(2)$(3)$(4)$(5)$(6)$(7)$(8)$(9)))
endef
define debuginfo
$(if $(subst n,,$(__debug__)),$(call _info,$(__indent__)$(1)$(2)$(3)$(4)$(5)$(6))$(7)$(8)$(9))
endef
# }}}

# Accumulation Utilities: accum_{setup,clear_all_vars} and import {{{
# This is the core part of the sub-dir build mechanism.
# accum_setup, setting up all the necessities for accumulation.
# Usage: $(call accum_setup,$(ACCUM_VARS))
define _accum_setup #{{{
# Define ALL_* vars {{{
# ALL_* must be recursive variable to handle the case when we have been included
# by a sub-Makefile, but in ordinary conditions, we must be careful to update the
# ALL_* immediately.
$(foreach v,$(1),$$(eval ALL_$v = $$$$($v)))
# }}}
# Define accum_clear_all_vars
accum_clear_all_vars = $(foreach v,$(1),$$(eval $v :=))
$$(call accum_clear_all_vars) # immediately clear all $$v's
# internal import helper function {{{
#                                |   1    |
# usage: $$(eval $$(call __import__,filename))
define __import__
__incfile := $$(subst //,/,$$(1))
P:=$$$$(dir $$$$(__incfile))
$$$$(call debuginfo,including $$$$(__incfile))
$$$$(call accum_clear_all_vars)
__indent__:=$$$$(empty)$$(__indent__)$$$$(space)$$$$(empty)
include $$$$(__incfile)
__indent__:=$$$$(empty)$$(__indent__)$$$$(empty)
P:=$$P
ifeq ($$$$(_accum_progs_),y)
 $$$$(eval $(foreach v,$(1),$$$$$$$$(eval ALL_$v+=$$$$($v))))
endif
# Extra empty line here, to avoid make joining the lines here

endef # }}}
# import: import the sub-Makefiles {{{
#                     |    1    |
# Usage: $$(call import,makefiles))
import = $$(eval $$(foreach f,$$(1),$$(call __import__,$$f)))
# }}}
endef # }}}
accum_setup = $(eval $(call _accum_setup,$(1)))
# }}}

# show-XXXX target {{{
ifeq ($(findstring show-,$(MAKECMDGOALS)),show-)
_show_var := $(subst show-,,$(MAKECMDGOALS))
$(warning $$($(_show_var)) = $(value $(_show_var)))
$(MAKECMDGOALS):
	@echo \$$\($(_show_var)\) = '$(value $(_show_var))'
endif
# }}}

ifeq ($(strip $(__debug__)),y)
 $(info Code root is at $P)
endif
# }}}

# Import will accumulate these variables into their ALL_* counterparts.
$(call accum_setup,TARGETS PROGS JUNKS JUNKDIRS CLEANDIRS PHONYS TESTS \
	DOWNLOADS INSTALLS UNINSTALL_RULES UNINSTALL_JUNKS UNINSTALL_JUNKDIRS NEEDED_BINS)

# internal. don't touch :) {{{
# if we aren't the first Makefile read in, it means that we are read in by 
# some sub-Makefiles, so don't accumulate further progs in other sub-Makefiles
_accum_progs_ := $(_is_main_makefile)
# }}}

# Define useful functions for me and our subdirs.
vlist         = $(subst $(space), \$(newline)  ,$(sort $1))
addfix        = $(addprefix $1,$(addsuffix $2,$3))
tobool        = $(if $(strip $(call _istrue,$1)),1,)
pathsearch    = $(firstword $(wildcard $(addsuffix /$1,$(subst :, ,$(PATH)))))
extract-vars  = $(foreach VAR,$(call addfix,$1,$2,$3),$($(VAR)))
inputs-noconf = $(filter-out %/mainconf.mk,$^)

define setbin
export $1 ?= $$(call pathsearch,$1)

$$(if $$($1),\
  $$(if $$(wildcard $$($1)),,\
    $$(warning Cannot find binary executable "$$($1)" for "$1") \
    $$(eval badbin := 1) \
  ), \
  $$(warning Cannot find binary executable in PATH for "$1") \
  $$(eval badbin := 1) \
)
endef

define _istrue
$(eval _tmp := $1)
$(foreach C,0 n o f a l s e N O F A L S E,$(eval _tmp := $(subst $C,,$(_tmp))))
$(_tmp)
endef

# Define our subdirs.
DIRS := confmake \
	bind9conf hosts iptables proxypac rssdesktop squidconf sshconf

# Enable second expansion support here.
.SECONDEXPANSION:

# Import all makefiles in subdirs.
# Remember to prefix every path with $P to get proper file path tracking.
$(call import,$(call addfix,$P,/Makefile,$(DIRS)))


# Some targets.
all: setbins $$(ALL_TARGETS) $$(ALL_PROGS)
clean:
	$(if $(strip $(ALL_JUNKS)),rm -f $(call vlist,$(ALL_JUNKS)))
	$(if $(strip $(ALL_JUNKDIRS)),rm -rf $(call vlist,$(ALL_JUNKDIRS)))
	@-$(if $(strip $(ALL_CLEANDIRS)),for i in $(strip $(ALL_CLEANDIRS)); do echo make -C \"$$i\" clean; make -C $$i clean; done)
test: setbins $$(ALL_TESTS)
download: setbins $$(ALL_DOWNLOADS)
install: setbins $$(ALL_INSTALLS)
uninstall: setbins $$(ALL_UNINSTALL_RULES)
	$(if $(strip $(ALL_UNINSTALL_JUNKS)),rm -f $(call vlist,$(ALL_UNINSTALL_JUNKS)))
	$(if $(strip $(ALL_UNINSTALL_JUNKDIRS)),rm -rf $(call vlist,$(ALL_UNINSTALL_JUNKDIRS)))
setbins:
	$(eval N := $$(sort $$(ALL_NEEDED_BINS)))
	@echo "Trying to find binary executables: $N"
	$(eval badbin := )
	$(foreach B,$N,$(eval $(call setbin,$B)))
	$(if $(badbin),$(error Some binary executables cannot be found),)
	$(eval badbin := )
	$(eval N := )

.PHONY: $$(ALL_PHONYS) $$(ALL_TESTS) all default clean test download install uninstall setbins

# at EOF, we handle complicated sub-directory build problem, don't touch {{{
$(call accum_clear_all_vars)
ifeq ($(_is_main_makefile),n)
 $(call debuginfo,Doing sub-dir build, I might re-include some Makefiles and my cmd might be redundant. Please bear with me.)
 # we need to set correct P for sub-Makefile that included us
 P:=$(dir $(firstword $(MAKEFILE_LIST)))
 _accum_progs_ := y
endif
# }}}

# vim: set fdm=marker noet :
