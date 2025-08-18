#!/bin/bash

LOG_FILE="/var/log/batch_cleanup.log"
TARGET_DIR="/tmp"
BATCH_SIZE=200
CI_LOG_DIR="/var/www/apex/public_html/application/logs"
DAYS_TO_KEEP=3

echo "[$(date)] Starting batch cleanup..." >> "$LOG_FILE"

# -------------------------------
# 1. CLEANUP /tmp FILES
# -------------------------------

# Find files (not directories) and loop in batches
find "$TARGET_DIR" -type f -atime +2 | while read -r file; do
    ((count++))
    echo "Deleting: $file"
    rm -f "$file"

    if (( count % BATCH_SIZE == 0 )); then
        echo "Batch of $BATCH_SIZE files deleted. Pausing for 5 seconds..."
        sleep 5
    fi
done

echo "[$(date)] Cleanup complete. Total deleted from /tmp: $count" >> "$LOG_FILE"


# -------------------------------
# 2. CLEANUP OLD CI LOG FILES
# -------------------------------
#!/bin/bash

today=$(date +%s)  # Current timestamp

for file in "$CI_LOG_DIR"/log-*.php; do
    [[ -f "$file" ]] || continue

    # Extract date part: log-YYYY-MM-DD.php → YYYY-MM-DD
    filename=$(basename "$file")
    file_date_part=$(echo "$filename" | sed -E 's/log-([0-9]{4}-[0-9]{2}-[0-9]{2})\.php/\1/')

    # Validate that date was properly extracted
    if [[ "$file_date_part" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
        file_timestamp=$(date -d "$file_date_part" +%s)
        age_days=$(( (today - file_timestamp) / 86400 ))

        if (( age_days > DAYS_TO_KEEP )); then
            echo "Deleting $file (age: $age_days days)"
            rm -f "$file"
            # echo "[DRY RUN] Would delete $file (age: $age_days days)"
        fi
    fi
done

