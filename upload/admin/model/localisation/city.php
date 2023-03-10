<?php
class ModelLocalisationCity extends Model {
	public function addCity($data) {
		$this->db->query("INSERT INTO  `" . DB_PREFIX . "city` SET `name` = '" . $this->db->escape($data['name']) . "', `postcode` = '" . $this->db->escape($data['postcode']) . "', `country_id` = '" . (int)$data['country_id'] . "', `zone_id` = '" . (int)$data['zone_id'] . "', `status` = '" . (int)$data['status'] . "'");

		$this->cache->delete('city');
	}

	public function editCity($city_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "city` SET `name` = '" . $this->db->escape($data['name']) . "', `postcode` = '" . $this->db->escape($data['postcode']) . "', `country_id` = '" . (int)$data['country_id'] . "', `zone_id` = '" . (int)$data['zone_id'] . "', status = '" . (int)$data['status'] . "' WHERE city_id = '" . (int)$city_id . "'");

		$this->cache->delete('city');
	}

	public function deleteCity($city_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "city` WHERE `city_id` = '" . (int)$city_id . "'");

		$this->cache->delete('city');
	}

	public function getCity($city_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "city` WHERE `city_id` = '" . (int)$city_id . "'");

		return $query->row;
	}

	public function getCities($data = array()) {
		$city_data = '';

		if ($data) {
			$sql = "SELECT *, c.city_id AS city_id, c.zone_id AS zone_id, c.name AS name, c.status AS status, (SELECT name FROM " . DB_PREFIX . "zone z WHERE z.zone_id = c.zone_id AND z.status = '1') AS zone FROM " . DB_PREFIX . "city c";

			$sort_data = array(
				'name',
				'postcode',
				'zone'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY zone";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$city_data = $this->cache->get('city');

			if (!$city_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` ORDER BY `name` ASC");

				$city_data = $query->rows;

				$this->cache->set('city', $city_data);
			}

			return $city_data;
		}
	}

	public function getCitiesByZoneId($zone_id) {
		$city_data = $this->cache->get('city.' . (int)$zone_id);

		if (!$city_data) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city` WHERE `zone_id` = '" . (int)$zone_id . "' AND `status` = '1' ORDER BY `name`");

			$city_data = $query->rows;

			$this->cache->set('city.' . (int)$zone_id, $city_data);
		}

		return $city_data;
	}

	public function getTotalCities() {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "city`");

		return $query->row['total'];
	}

	public function checkDatabase() {
		// Cities
		$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "city'");

		if (!$query->num_rows) {
			$this->db->query("CREATE TABLE `" . DB_PREFIX . "city` (
				`city_id` int(11) NOT NULL AUTO_INCREMENT,
				`country_id` int(11) NOT NULL,
				`zone_id` int(11) NOT NULL,
				`name` varchar(128) NOT NULL,
				`postcode` varchar(10) NOT NULL,
				`status` tinyint(1) NOT NULL DEFAULT '1',
				`sort_order` int(3) NOT NULL DEFAULT '0',
				PRIMARY KEY (`city_id`))
			ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		}
	}
}