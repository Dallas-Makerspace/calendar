<?php
use Migrations\AbstractMigration;

class AddUniqueIndexesToPrerequisites extends AbstractMigration
{
    /**
     * Add the unique indexes, aborting first when duplicate data exists.
     *
     * @return void
     */
    public function up()
    {
        $this->failOnDuplicateData();
        $this->table('prerequisites')
            ->addIndex(['name'], [
                'unique' => true,
                'name' => 'uq_prerequisites_name',
            ])
            ->addIndex(['ad_group'], [
                'unique' => true,
                'name' => 'uq_prerequisites_ad_group',
            ])
            ->update();
    }

    /**
     * Drop the unique indexes.
     *
     * @return void
     */
    public function down()
    {
        $this->table('prerequisites')
            ->removeIndexByName('uq_prerequisites_name')
            ->removeIndexByName('uq_prerequisites_ad_group')
            ->update();
    }

    /**
     * Abort before adding the unique indexes when production data already has
     * case-insensitive duplicates (the table collation is utf8_general_ci),
     * which would otherwise make the migration hard-fail mid-deploy.
     *
     * @return void
     * @throws \RuntimeException When duplicate name/ad_group rows exist.
     */
    protected function failOnDuplicateData()
    {
        foreach (['name' => 'uq_prerequisites_name', 'ad_group' => 'uq_prerequisites_ad_group'] as $column => $index) {
            $duplicates = $this->fetchAll(
                'SELECT LOWER(`' . $column . '`) AS value, COUNT(*) AS n, ' .
                'GROUP_CONCAT(`id` ORDER BY `id`) AS ids ' .
                'FROM `prerequisites` ' .
                'GROUP BY LOWER(`' . $column . '`) ' .
                'HAVING n > 1 LIMIT 10'
            );
            if (empty($duplicates)) {
                continue;
            }
            $listing = [];
            foreach ($duplicates as $row) {
                $listing[] = sprintf("'%s' (ids %s)", $row['value'], $row['ids']);
            }
            throw new \RuntimeException(sprintf(
                'Refusing to add unique index %s: %d duplicate value group(s) exist in ' .
                '`prerequisites`.`%s` (compared case-insensitively): %s. ' .
                'Delete or merge the duplicate rows first, then re-run the migration.',
                $index,
                count($duplicates),
                $column,
                implode(', ', $listing)
            ));
        }
    }
}
