<p align="center"><img width="500" height="389" alt="Screenshot 2026-02-06 050949" src="https://github.com/user-attachments/assets/3aae17f4-dba1-4037-b905-d02dc77c6005" /></p>
I have this set up to use a LAMP stack and a cron job to run `moonphase.php` once a day and output to a JSON file for use with `gethomepage`. The setup is straightforward.  

***  

1. **Upload Files**  
  On your webhost, put `weather.html` and `moonphase.php` in a directory that is publicly accessible, e.g.: ~/public_html/weather

3. **Create a Cron Job**
  Create a cron job that runs the php script once per day:  
  `0 1 * * * /usr/bin/php /home/user/public_html/weather/moonphase.php`

     \- This will create a file called `moonphase.json` with content like:  
     ```
     {
        "moon_phase": "🌖 Waning Gibbous",
        "sunset": "🌇 Sunset: 05:02 PM",
        "sunrise": "🌅 Sunrise: 07:40 AM",
        "link": "https://weatherapi.com/astronomy"
     }
     ```

3. **Update `services.yaml`**  
  Copy the sample snippet from `services.yaml` and add it to your actual `services.yaml` configuration file.
