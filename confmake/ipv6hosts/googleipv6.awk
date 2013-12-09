#!/usr/bin/awk -f

BEGIN {
	output = 0;
}

$0 ~ /^###Google/ {
	output = 2;
}

output > 0 {
	if ($0 ~ /^___/) {
		#output--;
		print "# ";
	}

	sub(/\r$/,"");
	print $0;
}
