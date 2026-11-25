# Normalization

## UNF

An illustrative farm activity sheet combines farmer, farm, crop, planting, lookup descriptions, and repeating harvest details in one relation. This is reconstructed for documentation because the assignment does not provide a literal UNF table.

## 1NF

Each harvest is a separate row and each field is atomic. There are no repeating harvest columns.

## 2NF

Farmer, farm, crop, planting, and harvest attributes are separated. A table's non-key fields depend on that table's full key.

## 3NF

Repeated category, season, soil, irrigation, status, grade, and remark descriptions are represented by lookup tables and foreign keys. The five core transaction/master entities and three security/account tables complete the 15-table design.
