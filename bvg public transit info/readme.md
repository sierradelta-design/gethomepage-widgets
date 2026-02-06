
<p align="center"><img width="1487" height="279" alt="Screenshot 2026-02-06 082008" src="https://github.com/user-attachments/assets/4ccd3526-8b25-4f97-9544-bdd678f0fb38" /></p>  

I have this set up on a LAMP stack with a cron job to run `pubtrans.php` once every eight minutes, then output to a JSON file for use with `homepage`.  
<p>&nbsp;</p>
The purpose of this script was to replicate a DAISY-Anzeiger on my Homepage dashboard. The data is publicly available through VBB at https://unternehmen.vbb.de/digitale-services/api and documentation for the REST API can be found here: https://v6.vbb.transport.rest

<p align="center">
  <img width="500" height="375" alt="BVG_DAISY_Bus" src="https://github.com/user-attachments/assets/474b9a61-bd13-4466-bf27-69743a69a658" />
  <img width="500" height="375" alt="BVG_DAISY_ubahn" src="https://github.com/user-attachments/assets/d199e688-11b8-4959-aff4-c7ddcbcfd1e2" />
</p>  

***  

1. **Upload Files**  
  On your webhost, put `pubtrans.php` in a directory that is publicly accessible, e.g.: ~/public_html/pubtrans

3. **Create a Cron Job**  
  Create a cron job that runs the php script once every eight minutes:  
  `*/8 * * * * /usr/bin/php /home/user/public_html/pubtrans/pubtrans.php`

     \- This will create a file called `pubtrans.json` with content like:  
     ```
     {
       "Jakob Kaiser Platz North": {
         "ubahn": [
           "U7→Rathaus Spandau 🕡06:25",
           "U7→Rathaus Spandau ⚠️06:21",
           "U7→Rathaus Spandau 🕡06:30",
           "U7→Rathaus Spandau 🕡06:35",
           "U7→Rathaus Spandau ⚠️06:41",
           "U7→Rathaus Spandau 🕡06:45"
         ],
         "bus": [
           "X21→Märkisches Viertel, Quickborner Str. 🕕06:17",
           "M21→Rosenthal Nord 🕕06:18",
           "123→S+U Hauptbahnhof ⚠️06:24",
           "M21→Rosenthal Nord 🕡06:28",
           "X21→Märkisches Viertel, Quickborner Str. 🕡06:37",
           "M21→Rosenthal Nord 🕡06:38"
         ]
       },
       "Jakob Kaiser Platz South": {
         "ubahn": [
           "U7→Rudow ⚠️06:20",
           "U7→Rudow 🕡06:23",
           "U7→Rudow 🕡06:28",
           "U7→Rudow 🕡06:33",
           "U7→Rudow 🕡06:38",
           "U7→Rudow 🕡06:43"
         ],
         "bus": [
           "109→S+U Zoologischer Garten ⚠️06:16",
           "M21→S+U Jungfernheide ⚠️06:17",
           "123→Saatwinkler Damm\/Mäckeritzwiesen ⚠️06:17",
           "X21→S+U Jungfernheide 🕡06:21",
           "M21→S+U Jungfernheide ⚠️06:30",
           "123→Saatwinkler Damm\/Mäckeritzwiesen ⚠️06:33"
         ]
       }
     }
     ```

3. **Update `services.yaml`**  
  Copy the sample snippets from `services.yaml` and `custom.css` and add it to your actual `services.yaml` and `custom.css` configuration files. 
