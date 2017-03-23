for i in *;
do
	sudo gpg --passphrase asg00dasitgets --batch --no-tty --yes --output ${i%.*} --decrypt "$i";
done

for f in *.zip; 
do 
	unzip -d "${f%*.zip}" "$f"; 
	cd  $f
	sudo mv *.txt /home/druportal/trisource/
	php /home/druportal/trisource/scripts/datafeed-processing.php
	cd ..
	sudo rm "$f"
done

