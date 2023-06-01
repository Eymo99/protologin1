

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

create table users
(
    id               int auto_increment
        primary key,
    username         varchar(50)                                        not null,
    password         varchar(255)                                       not null,
    created_at       datetime               default current_timestamp() null,
    login_attempts   int                    default 0                   null,
    last_login       datetime                                           null,
    ip_address       varchar(255)                                       null,
    browser          varchar(255)           default ''                  not null,
    operating_system varchar(255)                                       null,
    role             enum ('admin', 'user') default 'user'              not null,
    blocked_until    datetime                                           null,
    constraint username
        unique (username)
)
ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
