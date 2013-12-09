#!/usr/bin/gawk -f

{
	m = match($3, /([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/, a);

	if (m == 1) {
		mask = 0;

		for (i=1; i<=4; i++) {
			n = a[i];
			while (n != 0) {
				mask += (n % 2);
				n = int(n / 2);
			}
		}
		
		print $1 "\t" mask "\t" $3;
	}
}
