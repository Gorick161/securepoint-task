Securepoint – Logfile Analysis

Voraussetzungen
- PHP ≥ 8 mit `zlib`-Extension  
- Entpackte Logdatei: `data/updatev12-access-pseudonymized.log`


Task 1 – Top Serials
Zählt, wie oft eine `serial` im Log vorkommt (optional nur HTTP 200).

**Run**
php bin/task1.php data/updatev12-access-pseudonymized.log --top=10 --only-200

Task 2 – Multi-Device Lizenzen

Dekodiert das Feld specs (Base64 → gzip → JSON) und ermittelt, welche Lizenz (serial)
auf mehreren Geräten mit unterschiedlichen mac-Adressen verwendet wurde.

**Run**
php bin/task2.php data/updatev12-access-pseudonymized.log --top=10 --only-200

Bonus Task 3 – Hardware-Klassen

Analysiert die Geräte-Hardware aus den specs-Feldern und gruppiert sie anhand
von Eigenschaften wie architecture, cpu, mem_total, disk_root oder disk_data.
Gibt die häufigsten Hardwareklassen aus.

**Run**
php bin/bonusTask3.php data/updatev12-access-pseudonymized.log --top=20

Die Ergebnisse werden in dem out Ordner in jeweils einer csv datei erstellt.
