# Migration vers version 2.0 - Gestion séparée palettes/cartons

## Contexte

La version 1.0 gérait un seul compteur global. La version 2.0 sépare :
- **Palettes** : ajoutées par la récolte, retirées par le traitement
- **Cartons** : ajoutés par le traitement, retirés par la revente

## Étapes de migration

### 1. Sauvegarder vos données actuelles

```bash
# Sur le serveur
mysqldump -u gestion_convois -p gestion_convois > /tmp/backup_avant_migration_$(date +%Y%m%d).sql
```

### 2. Exécuter la migration SQL

```bash
sudo mysql -u gestion_convois -p gestion_convois < /var/www/gestion_transport/migrations/upgrade_palettes_cartons.sql
```

**OU manuellement** dans MariaDB :

```bash
sudo mysql -u gestion_convois -p gestion_convois
```

```sql
-- Ajouter les colonnes palettes et cartons au coffre
ALTER TABLE coffre 
  ADD COLUMN quantite_palettes INT NOT NULL DEFAULT 0 AFTER quantite_actuelle,
  ADD COLUMN quantite_cartons INT NOT NULL DEFAULT 0 AFTER quantite_palettes;

-- Migrer les données existantes (tout passe en palettes par défaut)
UPDATE coffre SET quantite_palettes = quantite_actuelle, quantite_cartons = 0;

-- Ajouter la colonne unite aux mouvements
ALTER TABLE mouvements 
  ADD COLUMN unite ENUM('palette','carton') NOT NULL DEFAULT 'palette' AFTER type;

-- Ajouter colonnes détaillées aux convois
ALTER TABLE convois
  ADD COLUMN quantite_palettes_entree INT DEFAULT 0 AFTER quantite_realisee,
  ADD COLUMN quantite_palettes_sortie INT DEFAULT 0 AFTER quantite_palettes_entree,
  ADD COLUMN quantite_cartons_entree INT DEFAULT 0 AFTER quantite_palettes_sortie,
  ADD COLUMN quantite_cartons_sortie INT DEFAULT 0 AFTER quantite_cartons_entree;

EXIT;
```

### 3. Remplacer les fichiers PHP

**Option A : Renommer les fichiers (recommandé pour test)**

```bash
cd /var/www/gestion_transport

# Sauvegarder les anciens fichiers
sudo cp public/index.php public/index_v1_backup.php
sudo cp templates/dashboard.php templates/dashboard_v1_backup.php
sudo cp templates/convoi_view.php templates/convoi_view_v1_backup.php
sudo cp templates/coffre.php templates/coffre_v1_backup.php

# Activer les nouvelles versions
sudo mv public/index_v2.php public/index.php
sudo mv templates/dashboard_v2.php templates/dashboard.php
sudo mv templates/convoi_view_v2.php templates/convoi_view.php
sudo mv templates/coffre_v2.php templates/coffre.php
```

**Option B : Via Git (si vous avez push les changements)**

```bash
cd /var/www/gestion_transport
sudo git stash
sudo git pull origin main
sudo git stash pop
```

### 4. Vérifier les permissions

```bash
sudo chown -R www-data:www-data /var/www/gestion_transport
sudo chmod 644 /var/www/gestion_transport/public/index.php
```

### 5. Tester l'application

Accédez à votre site web et vérifiez :
1. Le dashboard affiche "Palettes en stock" et "Cartons en stock"
2. La page Coffre montre les deux compteurs
3. Lors de la clôture d'un convoi :
   - **Récolte** : demande uniquement "Nombre de palettes"
   - **Traitement** : demande "Palettes à traiter" ET "Cartons produits"
   - **Revente** : demande uniquement "Nombre de cartons"

### 6. Rollback si problème

Si quelque chose ne fonctionne pas :

```bash
cd /var/www/gestion_transport

# Restaurer les anciens fichiers
sudo cp public/index_v1_backup.php public/index.php
sudo cp templates/dashboard_v1_backup.php templates/dashboard.php
sudo cp templates/convoi_view_v1_backup.php templates/convoi_view.php
sudo cp templates/coffre_v1_backup.php templates/coffre.php

# Restaurer la base de données
sudo mysql -u gestion_convois -p gestion_convois < /tmp/backup_avant_migration_YYYYMMDD.sql
```

## Changements principaux

### Base de données

**Table `coffre`** :
- ✅ `quantite_palettes` : stock de palettes
- ✅ `quantite_cartons` : stock de cartons
- ⚠️ `quantite_actuelle` : conservée temporairement (peut être supprimée après vérification)

**Table `mouvements`** :
- ✅ `unite` : 'palette' ou 'carton'

**Table `convois`** :
- ✅ `quantite_palettes_entree` : palettes ajoutées (récolte)
- ✅ `quantite_palettes_sortie` : palettes retirées (traitement)
- ✅ `quantite_cartons_entree` : cartons ajoutés (traitement)
- ✅ `quantite_cartons_sortie` : cartons retirés (revente)

### Logique métier

**Récolte** :
- Avant : ajoutait `quantite_actuelle`
- Maintenant : ajoute `quantite_palettes`

**Traitement** :
- Avant : retirait de `quantite_actuelle`
- Maintenant : retire des `quantite_palettes` ET ajoute des `quantite_cartons`

**Revente** :
- Avant : retirait de `quantite_actuelle`
- Maintenant : retire des `quantite_cartons`

## Vérifications post-migration

```bash
# Vérifier la structure de la table coffre
sudo mysql -u gestion_convois -p -e "DESCRIBE gestion_convois.coffre;"

# Vérifier les données du coffre
sudo mysql -u gestion_convois -p -e "SELECT * FROM gestion_convois.coffre;"

# Vérifier un convoi test
sudo mysql -u gestion_convois -p -e "SELECT id, type, statut, quantite_palettes_entree, quantite_palettes_sortie, quantite_cartons_entree, quantite_cartons_sortie FROM gestion_convois.convois LIMIT 5;"
```

## Notes importantes

- Les anciens convois (avant migration) auront les nouvelles colonnes à `NULL` ou `0`
- Le système est rétrocompatible : les anciens convois s'affichent correctement
- Les nouveaux convois utiliseront automatiquement la nouvelle logique

## Support

En cas de problème, vérifiez :
1. Les logs Apache : `sudo tail -f /var/log/apache2/gestion-convois-error.log`
2. Les erreurs PHP dans le navigateur (si activées)
3. La structure de la base de données avec `DESCRIBE`

---

**Migration terminée !** Vous pouvez maintenant gérer palettes et cartons séparément. 🎉
