-- ============================================================================
-- Copyright (C) 2014 Marcos García <marcosgdf@gmail.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

ALTER TABLE  llx_product_price ADD INDEX (  fk_user_author );
ALTER TABLE  llx_product_price ADD INDEX (  fk_product );

ALTER TABLE  llx_product_price ADD FOREIGN KEY (  fk_product ) REFERENCES  llx_product (
  rowid
) ON DELETE RESTRICT ON UPDATE RESTRICT ;

ALTER TABLE  llx_product_price ADD FOREIGN KEY (  fk_user_author ) REFERENCES  llx_user (
  rowid
) ON DELETE RESTRICT ON UPDATE RESTRICT ;