# IPQS-thing

Allows you to check a batch of email adresses in csv format with www.ipqualityscore.com, and save the output in another csv file.

## How to run

Move your input CSV file in `csv/`

Then run `php main.php API_KEY INPUT_FILE OUTPUT_FILE` where OUTPUT_FILE is optional and will be replaced with `export-{date}.csv` if empty.

(Note that the script will automatically look in 'csv/', so you only need to provide the file name)
