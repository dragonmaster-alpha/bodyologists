-- @see: https://stackoverflow.com/questions/24370975/find-distance-between-two-points-using-latitude-and-longitude-in-mysql
DELIMITER $$
CREATE FUNCTION `getDistance`(`lat1` VARCHAR(200), `lng1` VARCHAR(200), `lat2` VARCHAR(200), `lng2` VARCHAR(200)) RETURNS varchar(10) CHARSET utf8 READS SQL DATA
begin
    declare distance varchar(10);

    set distance = (select (6371 * acos( -- 6371 for kilometers, 3961 for miles
                    cos( radians(lat2) )
                    * cos( radians( lat1 ) )
                    * cos( radians( lng1 ) - radians(lng2) )
                    + sin( radians(lat2) )
                    * sin( radians( lat1 ) )
        ) ) as distance);

    if(distance is null)
    then
        return '';
    else
        return distance;
    end if;
end$$
DELIMITER ;