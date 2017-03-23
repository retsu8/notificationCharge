for f in *.zip; 
do 
	unzip -d "${f%*.zip}" "${f%.*}"; 
	cd  "${f%.*}";
	sudo rm FrtLnTran.txt;
	sudo mv *.txt /home/druportal/trisource/;
	php /home/druportal/trisource/scripts/datafeed-processing.php;
	cd ..;
	sudo rm "$f";
done
