# IPQS-thing

Allows you to check a batch of email adresses in csv format with www.ipqualityscore.com, and save the output in another csv file.

## How to run

Move your input CSV file in `csv/`

Then run `php main.php API_KEY INPUT_FILE` (note that the script will automatically look for files in 'csv/', so you only need to provide the file name)

If you wish to append new emails to an existing CSV file, or write to your own, you can provide an `OUTPUT_FILE`. This is optional, and it will be set to `export-{date}.csv` by default. So the command will look like `php main.php API_KEY INPUT_FILE OUTPUT_FILE`
