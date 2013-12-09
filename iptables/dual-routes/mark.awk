#!/usr/bin/awk -f
$1 !~ /^#/ && $2 ~ /./ {
	print "-A DUALPRE -d "$1"/"$2" -j MARK --set-mark " mark;
}
