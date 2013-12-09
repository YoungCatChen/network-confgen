_=$(if $(wildcard $1),include $1,$$(eval $$(call _,../$1)))
$(if $P,,$(eval $(call _,../GNUmakefile)))

ifeq ($(call tobool,$(sshconf_portmapping_enabled)),1)
TARGETS         += $Pssh_config_forward
JUNKS           += $Pssh_config_forward*
INSTALLS        += $P_install
UNINSTALL_RULES += $P_uninstall
NEEDED_BINS     += awk php ssh
endif

ifeq ($(sshconf_portmapping_custom_install_path),)
export sshconf_portmapping_custom_install_path := $(ssh_conf)/ssh_config_forward
endif

$P_install : export P := $P
$P_install : $P../../strip-generated $Pssh_config_forward $Pinit.conf $(ssh_conf)
	$(awk) -f $< $(sshconf_portmapping_custom_install_path) > /tmp/sshconf
	{ cat /tmp/sshconf; \
		echo '####==== Generated start ====####'; \
		cat $Pssh_config_forward; \
		echo '####==== Generated end ====####'; \
		} > $(sshconf_portmapping_custom_install_path)
	rm -f /tmp/sshconf
	install -m 755 -d /etc/init
	install -m 644 $Pinit.conf /etc/init/ssh-portmapping.conf

$P_uninstall : $P../../strip-generated
	rm -f /etc/init/ssh-portmapping.conf
	$(awk) -f $< $(sshconf_portmapping_custom_install_path) > /tmp/sshconf
	cat /tmp/sshconf > $(sshconf_portmapping_custom_install_path)
	rm -f /tmp/sshconf

$Pssh_config_forward : $Pinstall.php $(MAIN_CONF)
	$(php) $< $(ALL_INIS) \
		> $@~
	mv -f $@~ $@

